from django.db import models


class Item(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)

    code = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=45, blank=True, null=True)
    long_description = models.CharField(max_length=255, blank=True, null=True)

    stocked = models.IntegerField()
    active = models.IntegerField()

    # External reference tables (placeholders can be added later if needed)
    harmonisation_code = models.ForeignKey("HarmonisationCode", models.DO_NOTHING, blank=True, null=True)
    base_uom = models.ForeignKey("Uom", models.DO_NOTHING, blank=True, null=True)

    picture = models.CharField(max_length=255, blank=True, null=True)
    total_inventory = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True)
    std_cost = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True)

    forecasted = models.IntegerField(blank=True, null=True)
    forecast_batch = models.IntegerField(blank=True, null=True)
    bom_batch = models.DecimalField(max_digits=20, decimal_places=5, blank=True, null=True)

    prev_item = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "item"
