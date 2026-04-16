from django.db import models


class Loading(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    loading_type = models.ForeignKey("core_models.LoadingType", models.DO_NOTHING, blank=True, null=True)
    parent_loading = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    loading_start_time = models.DateTimeField(blank=True, null=True)
    loading_end_time = models.DateTimeField(blank=True, null=True)
    loading_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "loading"
