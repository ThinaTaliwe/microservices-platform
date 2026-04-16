from django.db import models


class ModeOfTransport(models.Model):
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "mode_of_transport"



class ShipmentTypeHasShipmentType(models.Model):
    id = models.BigAutoField(primary_key=True)
    parent_shipment_type = models.ForeignKey(
        "ShipmentType",
        models.DO_NOTHING,
        related_name="shipment_type_children",
    )
    child_shipment_type = models.ForeignKey(
        "ShipmentType",
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


class ShipmentType(models.Model):
    """
    Reference table defining the type of shipment
    (e.g. Human, Goods, Asset, etc.)
    """
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'shipment_type'

    mode_of_transport = models.ForeignKey(
        "core_models.ModeOfTransport",
        on_delete=models.DO_NOTHING,
        blank=True,
        null=True,
    )


class Shipment(models.Model):
    """
    Core shipment entity.
    Represents one complete shipment lifecycle.
    """
    shipment_type = models.ForeignKey(
        ShipmentType,
        on_delete=models.DO_NOTHING
    )

    bu = models.ForeignKey(
        'core_models.Bu',
        on_delete=models.DO_NOTHING
    )

    shipment_instruction = models.ForeignKey(
        'core_models.ShipmentInstruction',
        on_delete=models.DO_NOTHING
    )

    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)

    # Phase links
    loading = models.ForeignKey(
        'core_models.Loading',
        on_delete=models.DO_NOTHING
    )
    movement = models.ForeignKey(
        'core_models.Movement',
        on_delete=models.DO_NOTHING
    )
    offloading = models.ForeignKey(
        'core_models.Offloading',
        on_delete=models.DO_NOTHING
    )
    storage = models.ForeignKey(
        'core_models.Storage',
        on_delete=models.DO_NOTHING
    )

    # Phase timestamps
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
        db_table = 'shipment'
