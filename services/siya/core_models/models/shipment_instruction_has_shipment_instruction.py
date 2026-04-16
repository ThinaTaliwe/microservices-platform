from django.db import models


class ShipmentInstructionHasShipmentInstruction(models.Model):
    id = models.BigAutoField(primary_key=True)

    shipment_instruction = models.ForeignKey(
        "core_models.ShipmentInstruction",
        models.DO_NOTHING,
        related_name="child_instruction_links",
    )

    shipment_instruction_id1 = models.ForeignKey(
        "core_models.ShipmentInstruction",
        models.DO_NOTHING,
        db_column="shipment_instruction_id1",
        related_name="parent_instruction_links",
    )

    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    reference = models.CharField(max_length=255, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_instruction_has_shipment_instruction"
