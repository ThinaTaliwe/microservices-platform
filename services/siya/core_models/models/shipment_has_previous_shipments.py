from django.db import models


class ShipmentHasPreviousShipments(models.Model):
    id = models.BigAutoField(primary_key=True)

    shipment = models.ForeignKey(
        "core_models.Shipment",
        models.DO_NOTHING,
        related_name="previous_links",
    )
    previous_shipment = models.ForeignKey(
        "core_models.Shipment",
        models.DO_NOTHING,
        related_name="next_links",
    )

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment_has_previous_shipments"
