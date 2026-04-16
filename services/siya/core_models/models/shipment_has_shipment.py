from django.db import models


class ShipmentHasShipment(models.Model):
    id = models.BigAutoField(primary_key=True)

    parent_shipment = models.ForeignKey(
        "core_models.Shipment",
        models.DO_NOTHING,
        related_name="child_links",
    )
    child_shipment = models.ForeignKey(
        "core_models.Shipment",
        models.DO_NOTHING,
        related_name="parent_links",
    )

    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    code = models.CharField(max_length=45, blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_has_shipment"
