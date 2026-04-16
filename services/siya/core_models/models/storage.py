from django.db import models


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
