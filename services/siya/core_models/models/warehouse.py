from django.db import models


class Warehouse(models.Model):
    id = models.BigAutoField(primary_key=True)

    bu = models.ForeignKey("core_models.Bu", on_delete=models.DO_NOTHING)
    site = models.ForeignKey("core_models.Site", on_delete=models.DO_NOTHING, blank=True, null=True)

    short_code = models.CharField(max_length=5, blank=True, null=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)

    address = models.ForeignKey("core_models.Address", on_delete=models.DO_NOTHING)

    intransit_warehouse = models.IntegerField(blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "warehouse"
