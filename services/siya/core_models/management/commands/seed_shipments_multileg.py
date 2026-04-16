from __future__ import annotations

from decimal import Decimal
from datetime import timedelta

from django.core.management.base import BaseCommand
from django.db import transaction
from django.utils import timezone

from core_models.models import (
    Bu,
    ShipmentType,
    ModeOfTransport,
    InstructionType,
    LoadingType,
    ShipmentInstruction,
    ShipmentInstructionHasItem,
    ShipmentInstructionHasShipmentInstruction,
    Loading,
    LoadingHasItem,
    Movement,
    MovementHasItem,
    Offloading,
    OffloadingHasItem,
    Storage,
    StorageHasItem,
    Item,
    Shipment,
    ShipmentItems,
    ShipmentHasShipment,
    ShipmentHasPreviousShipments,
)


def dt(base, hours=0, minutes=0):
    return base + timedelta(hours=hours, minutes=minutes)


def s45(v: str | None) -> str | None:
    return v[:45] if v else v


def s255(v: str | None) -> str | None:
    return v[:255] if v else v


class Command(BaseCommand):
    help = "Idempotent + atomic: seed 1 master mixed-modal shipment + 3 sequential legs, with full item tracking."

    LINK_INSTRUCTION_HIERARCHY = True
    SEED_CODE = "DEMO_INTL_0001"  # used in reference/code fields for deterministic cleanup

    @transaction.atomic
    def handle(self, *args, **options):
        now = timezone.now()

        # ----------------------------
        # 0) Deterministic lookups
        # ----------------------------
        bu = Bu.objects.get(id=1)

        shipment_type_master = ShipmentType.objects.get(id=6)  # Mix Model freight
        shipment_type_air = ShipmentType.objects.get(id=1)     # Airfreigt
        shipment_type_road = ShipmentType.objects.get(id=2)    # Roadfreight
        shipment_type_sea = ShipmentType.objects.get(id=3)     # Seafreight

        mot_air = ModeOfTransport.objects.get(id=1)
        mot_road = ModeOfTransport.objects.get(id=2)
        mot_ocean = ModeOfTransport.objects.get(id=3)

        instruction_type_master = InstructionType.objects.get(id=7)  # import_shipping
        loading_type = LoadingType.objects.order_by("id").first()  # nullable in model

        # ----------------------------
        # 1) Cleanup previous seed run (idempotent)
        # ----------------------------
        # We locate the master instruction by its reference, then find shipments pointing to it.
        existing_master_instr = ShipmentInstruction.objects.filter(
            instruction_reference=f"{self.SEED_CODE}_MASTER"
        ).first()

        if existing_master_instr:
            existing_master_shipment = Shipment.objects.filter(
                shipment_instruction=existing_master_instr
            ).first()
        else:
            existing_master_shipment = None

        if existing_master_shipment:
            self.stdout.write("♻️ Existing seed found — deleting old graph first...")

            # Find children via shipment_has_shipment
            child_ids = list(
                ShipmentHasShipment.objects.filter(parent_shipment=existing_master_shipment)
                .values_list("child_shipment_id", flat=True)
            )
            all_shipments = [existing_master_shipment.id] + child_ids

            # 1) Delete link tables that depend on shipments
            ShipmentHasPreviousShipments.objects.filter(shipment_id__in=all_shipments).delete()
            ShipmentHasPreviousShipments.objects.filter(previous_shipment_id__in=all_shipments).delete()
            ShipmentHasShipment.objects.filter(parent_shipment_id__in=all_shipments).delete()
            ShipmentHasShipment.objects.filter(child_shipment_id__in=all_shipments).delete()

            # 2) Delete shipment item links
            ShipmentItems.objects.filter(shipment_id__in=all_shipments).delete()

            # 3) Delete stage item links (by stage IDs referenced by shipments)
            shipments_qs = Shipment.objects.filter(id__in=all_shipments).only(
                "loading_id", "movement_id", "offloading_id", "storage_id", "shipment_instruction_id"
            )

            loading_ids = [s.loading_id for s in shipments_qs if s.loading_id]
            movement_ids = [s.movement_id for s in shipments_qs if s.movement_id]
            offloading_ids = [s.offloading_id for s in shipments_qs if s.offloading_id]
            storage_ids = [s.storage_id for s in shipments_qs if s.storage_id]
            instr_ids = [s.shipment_instruction_id for s in shipments_qs if s.shipment_instruction_id]

            LoadingHasItem.objects.filter(loading_id__in=loading_ids).delete()
            MovementHasItem.objects.filter(movement_id__in=movement_ids).delete()
            OffloadingHasItem.objects.filter(offloading_id__in=offloading_ids).delete()
            StorageHasItem.objects.filter(storage_id__in=storage_ids).delete()

            # 4) Instruction item links + instruction hierarchy links
            ShipmentInstructionHasItem.objects.filter(shipment_instruction_id__in=instr_ids).delete()

            ShipmentInstructionHasShipmentInstruction.objects.filter(
                shipment_instruction_id__in=instr_ids
            ).delete()
            ShipmentInstructionHasShipmentInstruction.objects.filter(
                shipment_instruction_id1_id__in=instr_ids
            ).delete()

            # 5) Delete shipments (must happen before deleting stages because shipment FK points to stages)
            Shipment.objects.filter(id__in=all_shipments).delete()

            # 6) Delete stages now (no longer referenced)
            Loading.objects.filter(id__in=loading_ids).delete()
            Movement.objects.filter(id__in=movement_ids).delete()
            Offloading.objects.filter(id__in=offloading_ids).delete()
            Storage.objects.filter(id__in=storage_ids).delete()

            # 7) Delete instructions
            ShipmentInstruction.objects.filter(id__in=instr_ids).delete()

            self.stdout.write(self.style.SUCCESS("✅ Old seed graph deleted."))

        # ----------------------------
        # 2) Ensure stable demo items exist
        # ----------------------------
        item_specs = [
            ("DEMO-ITEM-001", "Demo Item 1", "Demo: Cartons of electronics", Decimal("100.000000")),
            ("DEMO-ITEM-002", "Demo Item 2", "Demo: Palletised textiles", Decimal("50.000000")),
            ("DEMO-ITEM-003", "Demo Item 3", "Demo: Spare parts crate", Decimal("20.000000")),
        ]

        items = []
        for code, desc, long_desc, qty in item_specs:
            itm = Item.objects.filter(bu=bu, code=code).first()
            if not itm:
                itm = Item.objects.create(
                    bu=bu,
                    code=code,
                    description=s45(desc),
                    long_description=s255(long_desc),
                    stocked=1,
                    active=1,
                    total_inventory=qty.quantize(Decimal("0.00000")),
                    std_cost=Decimal("0.00000"),
                    created_at=now,
                    updated_at=now,
                )
            items.append((itm, qty))

        # ----------------------------
        # 3) Helpers
        # ----------------------------
        def create_loading(parent, ref, start, end):
            return Loading.objects.create(
                bu=bu,
                loading_type=loading_type,
                parent_loading=parent,
                loading_start_time=start,
                loading_end_time=end,
                loading_reference=s255(ref),
                created_at=now,
                updated_at=now,
            )

        def create_movement(parent, ref, start, end):
            return Movement.objects.create(
                bu=bu,
                parent_movement=parent,
                movement_start_time=start,
                movement_end_time=end,
                movement_reference=s255(ref),
                created_at=now,
                updated_at=now,
            )

        def create_offloading(parent, ref, start, end):
            return Offloading.objects.create(
                bu=bu,
                loading_type=loading_type,
                parent_offloading=parent,
                offloading_start_time=start,
                offloading_end_time=end,
                offloading_reference=s255(ref),
                created_at=now,
                updated_at=now,
            )

        def create_storage(parent, ref, start, end):
            return Storage.objects.create(
                bu=bu,
                parent_storage=parent,
                storage_start_time=start,
                storage_end_time=end,
                storage_refence=s255(ref),  # typo preserved
                created_at=now,
                updated_at=now,
            )

        def link_shipment_items(shipment: Shipment, item_quantities):
            for itm, qty in item_quantities:
                ShipmentItems.objects.create(
                    shipment=shipment,
                    item=itm,
                    quantity=qty,
                    created_at=now,
                    updated_at=now,
                )

        def link_instruction_items(instr: ShipmentInstruction, item_quantities):
            for itm, qty in item_quantities:
                ShipmentInstructionHasItem.objects.create(
                    shipment_instruction=instr,
                    item=itm,
                    quantity=qty,
                    created_at=now,
                    updated_at=now,
                )

        def link_stage_items(loading, movement, offloading, storage, item_quantities):
            for itm, qty in item_quantities:
                LoadingHasItem.objects.create(loading=loading, item=itm, quantity=qty, created_at=now, updated_at=now)
                MovementHasItem.objects.create(movement=movement, item=itm, quantity=qty, created_at=now, updated_at=now)
                OffloadingHasItem.objects.create(offloading=offloading, item=itm, quantity=qty, created_at=now, updated_at=now)
                StorageHasItem.objects.create(storage=storage, item=itm, quantity=qty, created_at=now, updated_at=now)

        # ----------------------------
        # 4) MASTER shipment + stages
        # ----------------------------
        master_instruction = ShipmentInstruction.objects.create(
            bu=bu,
            instruction_type=instruction_type_master,
            instruction_reference=s45(f"{self.SEED_CODE}_MASTER"),
            instruction_detail=s255("Master shipment: Mixed modal, sequential legs SEA -> AIR -> ROAD (full quantity transfer)."),
            created_at=now,
            updated_at=now,
        )

        master_loading = create_loading(None, f"{self.SEED_CODE}_LOAD_MASTER", dt(now, hours=-120), dt(now, hours=-119, minutes=30))
        master_movement = create_movement(None, f"{self.SEED_CODE}_MOVE_MASTER", dt(now, hours=-119), dt(now, hours=-96))
        master_offloading = create_offloading(None, f"{self.SEED_CODE}_OFFLOAD_MASTER", dt(now, hours=-96), dt(now, hours=-95, minutes=30))
        master_storage = create_storage(None, f"{self.SEED_CODE}_STORE_MASTER", dt(now, hours=-95), dt(now, hours=-90))

        master_shipment = Shipment.objects.create(
            shipment_type=shipment_type_master,
            mode_of_transport=None,
            bu=bu,
            shipment_instruction=master_instruction,
            name=s45("DEMO Intl Master"),
            description=s255("Master mixed-modal shipment: children SEA/AIR/ROAD; sequenced chain; full quantity transfer."),
            loading=master_loading,
            movement=master_movement,
            offloading=master_offloading,
            storage=master_storage,
            loading_started=master_loading.loading_start_time,
            loading_ended=master_loading.loading_end_time,
            movement_started=master_movement.movement_start_time,
            movement_ended=master_movement.movement_end_time,
            offloading_started=master_offloading.offloading_start_time,
            offloading_ended=master_offloading.offloading_end_time,
            storage_started=master_storage.storage_start_time,
            storage_ended=master_storage.storage_end_time,
            created_at=now,
            updated_at=now,
        )

        link_shipment_items(master_shipment, items)
        link_instruction_items(master_instruction, items)
        link_stage_items(master_loading, master_movement, master_offloading, master_storage, items)

        # ----------------------------
        # 5) Legs (sequential, full qty)
        # ----------------------------
        legs = [
            ("SEA",  shipment_type_sea,  mot_ocean, -92, 36),
            ("AIR",  shipment_type_air,  mot_air,   -52, 12),
            ("ROAD", shipment_type_road, mot_road,  -35, 10),
        ]

        created_legs: list[Shipment] = []
        created_instrs: list[ShipmentInstruction] = []

        for idx, (prefix, stype, mot, offset_hours, duration_hours) in enumerate(legs, start=1):
            leg_instr = ShipmentInstruction.objects.create(
                bu=bu,
                instruction_type=instruction_type_master,
                instruction_reference=s45(f"{self.SEED_CODE}_{prefix}_INSTR_{idx:02d}"),
                instruction_detail=s255(f"Leg {prefix} under master; sequential chain; full quantity transfer."),
                created_at=now,
                updated_at=now,
            )

            base = dt(now, hours=offset_hours)
            leg_loading = create_loading(master_loading, f"{self.SEED_CODE}_LOAD_{prefix}", base, dt(base, minutes=45))
            leg_movement = create_movement(master_movement, f"{self.SEED_CODE}_MOVE_{prefix}", dt(base, hours=1), dt(base, hours=1 + duration_hours))
            leg_offloading = create_offloading(master_offloading, f"{self.SEED_CODE}_OFFLOAD_{prefix}", dt(base, hours=2 + duration_hours), dt(base, hours=2 + duration_hours, minutes=30))
            leg_storage = create_storage(master_storage, f"{self.SEED_CODE}_STORE_{prefix}", dt(base, hours=3 + duration_hours), dt(base, hours=6 + duration_hours))

            leg_shipment = Shipment.objects.create(
                shipment_type=stype,
                mode_of_transport=mot,
                bu=bu,
                shipment_instruction=leg_instr,
                name=s45(f"DEMO {prefix} Leg"),
                description=s255(f"Leg {prefix}: child of master; sequential chain; full quantity transfer."),
                loading=leg_loading,
                movement=leg_movement,
                offloading=leg_offloading,
                storage=leg_storage,
                loading_started=leg_loading.loading_start_time,
                loading_ended=leg_loading.loading_end_time,
                movement_started=leg_movement.movement_start_time,
                movement_ended=leg_movement.movement_end_time,
                offloading_started=leg_offloading.offloading_start_time,
                offloading_ended=leg_offloading.offloading_end_time,
                storage_started=leg_storage.storage_start_time,
                storage_ended=leg_storage.storage_end_time,
                created_at=now,
                updated_at=now,
            )

            link_shipment_items(leg_shipment, items)
            link_instruction_items(leg_instr, items)
            link_stage_items(leg_loading, leg_movement, leg_offloading, leg_storage, items)

            ShipmentHasShipment.objects.create(
                parent_shipment=master_shipment,
                child_shipment=leg_shipment,
                name=s45(f"MASTER->{prefix}"),
                description=s255("Child leg shipment under master shipment."),
                code=s45(f"{self.SEED_CODE}_{prefix}"),
            )

            created_legs.append(leg_shipment)
            created_instrs.append(leg_instr)

        # Previous chain: SEA -> AIR -> ROAD
        sea, air, road = created_legs
        ShipmentHasPreviousShipments.objects.create(shipment=air, previous_shipment=sea, created_at=now, updated_at=now)
        ShipmentHasPreviousShipments.objects.create(shipment=road, previous_shipment=air, created_at=now, updated_at=now)

        # Optional instruction hierarchy
        if self.LINK_INSTRUCTION_HIERARCHY:
            for idx, leg_instr in enumerate(created_instrs, start=1):
                ShipmentInstructionHasShipmentInstruction.objects.create(
                    shipment_instruction=master_instruction,
                    shipment_instruction_id1=leg_instr,
                    name=s45(f"MASTER->LEG-{idx:02d}"),
                    description=s255("Master instruction linked to leg instruction."),
                    reference=s255(f"{self.SEED_CODE}_MASTER_TO_{leg_instr.instruction_reference}"),
                    created_at=now,
                    updated_at=now,
                )

        self.stdout.write(self.style.SUCCESS("✅ Seed complete (idempotent + atomic)."))
        self.stdout.write(f"Master shipment ID: {master_shipment.id}")
        self.stdout.write("Leg shipment IDs: " + ", ".join(str(s.id) for s in created_legs))
