from django.db import models


class Uom(models.Model):
    id = models.BigAutoField(primary_key=True)

    name = models.CharField(max_length=255, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    symbol = models.CharField(max_length=50, blank=True, null=True)

    base_uom = models.ForeignKey("self", on_delete=models.DO_NOTHING, blank=True, null=True)
    base_uom_ratio = models.DecimalField(max_digits=20, decimal_places=10, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "uom"
