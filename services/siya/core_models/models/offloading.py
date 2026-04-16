from django.db import models


class Offloading(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    loading_type = models.ForeignKey("core_models.LoadingType", models.DO_NOTHING, blank=True, null=True)
    parent_offloading = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    offloading_start_time = models.DateTimeField(blank=True, null=True)
    offloading_end_time = models.DateTimeField(blank=True, null=True)
    offloading_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "offloading"
