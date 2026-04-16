from django.db import models


class Location(models.Model):
    id = models.BigAutoField(primary_key=True)

    bu = models.ForeignKey("core_models.Bu", on_delete=models.DO_NOTHING)
    warehouse = models.ForeignKey("core_models.Warehouse", on_delete=models.DO_NOTHING)

    short_code = models.CharField(max_length=10, blank=True, null=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "location"
