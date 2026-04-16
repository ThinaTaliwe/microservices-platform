from django.db import models


class Shipment(models.Model):
    shipment_type = models.ForeignKey(
        "core_models.ShipmentType",
        on_delete=models.DO_NOTHING
    )

    mode_of_transport = models.ForeignKey(
        "core_models.ModeOfTransport",
        on_delete=models.DO_NOTHING,
        blank=True,
        null=True,
    )

    bu = models.ForeignKey(
        "core_models.Bu",
        on_delete=models.DO_NOTHING
    )

    shipment_instruction = models.ForeignKey(
        "core_models.ShipmentInstruction",
        on_delete=models.DO_NOTHING
    )

    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)

    loading = models.ForeignKey("core_models.Loading", on_delete=models.DO_NOTHING)
    movement = models.ForeignKey("core_models.Movement", on_delete=models.DO_NOTHING)
    offloading = models.ForeignKey("core_models.Offloading", on_delete=models.DO_NOTHING)
    storage = models.ForeignKey("core_models.Storage", on_delete=models.DO_NOTHING)

    loading_started = models.DateTimeField(blank=True, null=True)
    loading_ended = models.DateTimeField(blank=True, null=True)

    movement_started = models.DateTimeField(blank=True, null=True)
    movement_ended = models.DateTimeField(blank=True, null=True)

    offloading_started = models.DateTimeField(blank=True, null=True)
    offloading_ended = models.DateTimeField(blank=True, null=True)

    storage_started = models.DateTimeField(blank=True, null=True)
    storage_ended = models.DateTimeField(blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "shipment"
