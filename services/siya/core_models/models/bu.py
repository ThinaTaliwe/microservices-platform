from django.db import models


class Bu(models.Model):
    id = models.BigAutoField(primary_key=True)
    bu_name = models.CharField(max_length=255)
    short_code = models.CharField(max_length=255, blank=True, null=True)
    sub_unit = models.IntegerField(blank=True, null=True)

    parent_bu = models.ForeignKey("self", on_delete=models.DO_NOTHING, blank=True, null=True)

    # keep as string refs (we are not modelling these tables now)
    company = models.ForeignKey("core_models.Company", on_delete=models.DO_NOTHING)
    system = models.ForeignKey("core_models.System", on_delete=models.DO_NOTHING)

    ops_currency = models.ForeignKey("core_models.Currency", on_delete=models.DO_NOTHING, related_name="bu_ops_currency_set")
    report_currency = models.ForeignKey("core_models.Currency", on_delete=models.DO_NOTHING, related_name="bu_report_currency_set")

    country = models.ForeignKey("core_models.Country", on_delete=models.DO_NOTHING)

    default_warehouse = models.ForeignKey(
        "core_models.Warehouse",
        models.DO_NOTHING,
        blank=True,
        null=True,
        related_name="default_for_bus",
    )

    logo = models.CharField(max_length=255, blank=True, null=True)
    config_path = models.CharField(max_length=255, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "bu"
