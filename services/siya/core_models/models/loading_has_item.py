from django.db import models


class LoadingHasItem(models.Model):
    id = models.BigAutoField(primary_key=True)
    loading = models.ForeignKey("core_models.Loading", on_delete=models.DO_NOTHING)
    item = models.ForeignKey("core_models.Item", on_delete=models.DO_NOTHING)
    quantity = models.DecimalField(max_digits=20, decimal_places=6, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "loading_has_item"
