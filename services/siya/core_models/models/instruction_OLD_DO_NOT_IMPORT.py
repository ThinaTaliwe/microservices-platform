from django.db import models


class ShipmentInstruction(models.Model):
    id = models.BigAutoField(primary_key=True)

    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)

    # External reference tables (we can model them later if needed)
    instruction_type = models.ForeignKey("InstructionType", models.DO_NOTHING)

    instruction_reference = models.CharField(max_length=45, blank=True, null=True)
    instruction_detail = models.CharField(max_length=255, blank=True, null=True)

    from_address = models.ForeignKey("Address", models.DO_NOTHING, blank=True, null=True, related_name="+")
    to_address = models.ForeignKey("Address", models.DO_NOTHING, blank=True, null=True, related_name="+")

    from_warehouse = models.ForeignKey("Warehouse", models.DO_NOTHING, blank=True, null=True, related_name="+")
    to_warehouse = models.ForeignKey("Warehouse", models.DO_NOTHING, blank=True, null=True, related_name="+")

    from_location = models.ForeignKey("Location", models.DO_NOTHING, blank=True, null=True, related_name="+")
    to_location = models.ForeignKey("Location", models.DO_NOTHING, blank=True, null=True, related_name="+")

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_instruction"


class ShipmentInstructionHasItem(models.Model):
    """
    Planned items/quantities on the instruction (the "plan").
    """
    id = models.BigAutoField(primary_key=True)
    shipment_instruction = models.ForeignKey(ShipmentInstruction, models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", models.DO_NOTHING)

    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_instruction_has_item"
