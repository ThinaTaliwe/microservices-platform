from django.db import models


class ShipmentInstructionHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    shipment_instruction = models.ForeignKey("core_models.ShipmentInstruction", models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", models.DO_NOTHING)

    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_instruction_has_item"
