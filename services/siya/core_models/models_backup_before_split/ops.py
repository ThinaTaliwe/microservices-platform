from django.db import models


class LoadingType(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "loading_type"


class Loading(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    loading_type = models.ForeignKey(LoadingType, models.DO_NOTHING)
    parent_loading = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    loading_start_time = models.DateTimeField(blank=True, null=True)
    loading_end_time = models.DateTimeField(blank=True, null=True)
    loading_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "loading"

class LoadingHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    loading = models.ForeignKey("Loading", on_delete=models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", on_delete=models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "loading_has_item"


class Movement(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    parent_movement = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    movement_start_time = models.DateTimeField(blank=True, null=True)
    movement_end_time = models.DateTimeField(blank=True, null=True)
    movement_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "movement"

class MovementHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    movement = models.ForeignKey("Movement", on_delete=models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", on_delete=models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "movement_has_item"



class Offloading(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    loading_type = models.ForeignKey(LoadingType, models.DO_NOTHING)
    parent_offloading = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    offloading_start_time = models.DateTimeField(blank=True, null=True)
    offloading_end_time = models.DateTimeField(blank=True, null=True)
    offloading_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "offloading"

class OffloadingHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    offloading = models.ForeignKey("Offloading", on_delete=models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", on_delete=models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "offloading_has_item"

class Storage(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    parent_storage = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    storage_start_time = models.DateTimeField(blank=True, null=True)
    storage_end_time = models.DateTimeField(blank=True, null=True)

    # NOTE: DB column has typo "storage_refence" so we must keep it.
    storage_refence = models.CharField(max_length=255, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "storage"
        
class StorageHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    storage = models.ForeignKey("Storage", on_delete=models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", on_delete=models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "storage_has_item"