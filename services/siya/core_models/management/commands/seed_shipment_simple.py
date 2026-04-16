from decimal import Decimal
from django.core.management.base import BaseCommand
from django.db import transaction
from django.utils import timezone
from core_models import models as M


class Command(BaseCommand):
    help = "Seed one simple shipment lifecycle + items and all *_has_item links (atomic)."

    def get_model(self, *names):
        for n in names:
            if hasattr(M, n):
                return getattr(M, n)
        raise AttributeError(f"None of these models exist in core_models.models: {names}")

    def pick_required(self, model, pk, label):
        obj = model.objects.filter(pk=pk).first()
        if not obj:
            raise RuntimeError(f"Missing required lookup row: {label} pk={pk} in table {model._meta.db_table}")
        return obj

    def ensure_uom(self):
        # Item.base_uom can be required. If there is no UOM row, create a self-referencing one.
        uom = M.Uom.objects.first()
        if uom:
            return uom

        # Create with explicit PK so we can point base_uom to itself on insert
        u = M.Uom(id=999999, name="EA", description="Each", symbol="ea")
        u.base_uom = u  # self FK
        u.base_uom_ratio = Decimal("1.0")
        u.created_at = timezone.now()
        u.updated_at = timezone.now()
        u.save(force_insert=True)
        return u

    def create_item(self, bu, base_uom, code, description):
        # Minimal safe item create that matches your actual Item fields
        return M.Item.objects.create(
            bu=bu,
            code=code,
            description=description,
            base_uom=base_uom,
            stocked=True,
            active=True,
            created_at=timezone.now(),
            updated_at=timezone.now(),
        )

    def handle(self, *args, **options):
        NOW = timezone.now()

        ShipmentItems = self.get_model("ShipmentItems", "ShipmentItem")
        LoadingHasItem = self.get_model("LoadingHasItem")
        MovementHasItem = self.get_model("MovementHasItem")
        OffloadingHasItem = self.get_model("OffloadingHasItem")
        StorageHasItem = self.get_model("StorageHasItem")

        with transaction.atomic():
            # ---- Required existing rows (these must exist) ----
            bu = self.pick_required(M.Bu, 1, "Bu")

            shipment_type = self.pick_required(M.ShipmentType, 6, "ShipmentType")
            mode_of_transport = self.pick_required(M.ModeOfTransport, 6, "ModeOfTransport")
            instruction_type = self.pick_required(M.InstructionType, 1, "InstructionType")
            loading_type = self.pick_required(M.LoadingType, 1, "LoadingType")

            # ---- Ensure we can create Item.base_uom ----
            base_uom = self.ensure_uom()

            # ---- Create a few items ----
            items = [
                self.create_item(bu, base_uom, "DEMO-ITEM-001", "Demo Item 1 (Box)"),
                self.create_item(bu, base_uom, "DEMO-ITEM-002", "Demo Item 2 (Pallet)"),
                self.create_item(bu, base_uom, "DEMO-ITEM-003", "Demo Item 3 (Crate)"),
            ]

            # ---- Create instruction ----
            instruction = M.ShipmentInstruction.objects.create(
                bu=bu,
                instruction_type=instruction_type,
                instruction_reference="DEMO-INSTR-001",
                instruction_detail="Simple demo instruction",
                created_at=NOW,
                updated_at=NOW,
            )

            # Optional planned quantities on instruction (the "plan")
            for i, item in enumerate(items, start=1):
                M.ShipmentInstructionHasItem.objects.create(
                    shipment_instruction=instruction,
                    item=item,
                    quantity=Decimal(str(i * 10)),
                    created_at=NOW,
                    updated_at=NOW,
                )

            # ---- Create phases ----
            loading = M.Loading.objects.create(
                bu=bu,
                loading_type=loading_type,
                loading_start_time=NOW,
                loading_end_time=NOW,
                loading_reference="DEMO-LOAD-001",
                created_at=NOW,
                updated_at=NOW,
            )

            movement = M.Movement.objects.create(
                bu=bu,
                movement_start_time=NOW,
                movement_end_time=NOW,
                movement_reference="DEMO-MOVE-001",
                created_at=NOW,
                updated_at=NOW,
            )

            offloading = M.Offloading.objects.create(
                bu=bu,
                loading_type=loading_type,  # your Offloading model uses loading_type
                offloading_start_time=NOW,
                offloading_end_time=NOW,
                offloading_reference="DEMO-OFF-001",
                created_at=NOW,
                updated_at=NOW,
            )

            storage = M.Storage.objects.create(
                bu=bu,
                storage_start_time=NOW,
                storage_end_time=NOW,
                storage_refence="DEMO-STORE-001",  # typo matches DB column
                created_at=NOW,
                updated_at=NOW,
            )

            # ---- Create shipment ----
            shipment = M.Shipment.objects.create(
                shipment_type=shipment_type,
                mode_of_transport=mode_of_transport,
                bu=bu,
                shipment_instruction=instruction,
                name="DEMO-SHIPMENT-001",
                description="Simple shipment lifecycle demo",
                loading=loading,
                movement=movement,
                offloading=offloading,
                storage=storage,
                loading_started=NOW,
                loading_ended=NOW,
                movement_started=NOW,
                movement_ended=NOW,
                offloading_started=NOW,
                offloading_ended=NOW,
                storage_started=NOW,
                storage_ended=NOW,
                created_at=NOW,
                updated_at=NOW,
            )

            # ---- Link items to shipment + each phase ----
            # ShipmentItems
            for i, item in enumerate(items, start=1):
                ShipmentItems.objects.create(
                    shipment=shipment,
                    item=item,
                    quantity=Decimal(str(i * 5)),
                    created_at=NOW,
                    updated_at=NOW,
                )

            # LoadingHasItem / MovementHasItem / OffloadingHasItem / StorageHasItem
            for i, item in enumerate(items, start=1):
                qty = Decimal(str(i * 5))

                LoadingHasItem.objects.create(
                    loading=loading, item=item, quantity=qty, created_at=NOW, updated_at=NOW
                )
                MovementHasItem.objects.create(
                    movement=movement, item=item, quantity=qty, created_at=NOW, updated_at=NOW
                )
                OffloadingHasItem.objects.create(
                    offloading=offloading, item=item, quantity=qty, created_at=NOW, updated_at=NOW
                )
                StorageHasItem.objects.create(
                    storage=storage, item=item, quantity=qty, created_at=NOW, updated_at=NOW
                )

        # If we get here, transaction committed successfully
        self.stdout.write(self.style.SUCCESS("✅ SIMPLE SHIPMENT + ITEMS SEEDED (atomic)"))
        self.stdout.write(f"Shipment id: {shipment.id}")
        self.stdout.write(f"Items created: {len(items)}")
