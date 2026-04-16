from django.db import models


class OffloadingHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    offloading = models.ForeignKey("core_models.Offloading", on_delete=models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", on_delete=models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "offloading_has_item"
