from django.db import models


class ShipmentTypeHasShipmentType(models.Model):
    id = models.BigAutoField(primary_key=True)

    parent_shipment_type = models.ForeignKey(
        "core_models.ShipmentType",
        models.DO_NOTHING,
        related_name="shipment_type_children",
    )
    child_shipment_type = models.ForeignKey(
        "core_models.ShipmentType",
        models.DO_NOTHING,
        related_name="shipment_type_parents",
    )

    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_type_has_shipment_type"
