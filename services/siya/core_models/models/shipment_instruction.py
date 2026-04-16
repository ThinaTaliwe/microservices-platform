from django.db import models


class ShipmentInstruction(models.Model):
    id = models.BigAutoField(primary_key=True)

    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)

    instruction_type = models.ForeignKey("core_models.InstructionType", models.DO_NOTHING)

    instruction_reference = models.CharField(max_length=45, blank=True, null=True)
    instruction_detail = models.CharField(max_length=255, blank=True, null=True)

    from_address = models.ForeignKey("core_models.Address", models.DO_NOTHING, blank=True, null=True, related_name="+")
    to_address = models.ForeignKey("core_models.Address", models.DO_NOTHING, blank=True, null=True, related_name="+")

    from_warehouse = models.ForeignKey("core_models.Warehouse", models.DO_NOTHING, blank=True, null=True, related_name="+")
    to_warehouse = models.ForeignKey("core_models.Warehouse", models.DO_NOTHING, blank=True, null=True, related_name="+")

    from_location = models.ForeignKey("core_models.Location", models.DO_NOTHING, blank=True, null=True, related_name="+")
    to_location = models.ForeignKey("core_models.Location", models.DO_NOTHING, blank=True, null=True, related_name="+")

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_instruction"
