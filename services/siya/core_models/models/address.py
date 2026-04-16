from django.db import models


class Address(models.Model):
    id = models.BigAutoField(primary_key=True)

    adress_type = models.ForeignKey("core_models.AdressType", on_delete=models.DO_NOTHING)

    name = models.CharField(max_length=255, blank=True, null=True)
    line1 = models.CharField(max_length=255, blank=True, null=True)
    line2 = models.CharField(max_length=255, blank=True, null=True)
    line3 = models.CharField(max_length=255, blank=True, null=True)
    p_o_box = models.CharField(max_length=255, blank=True, null=True)

    suburb = models.CharField(max_length=255, blank=True, null=True)

    city = models.ForeignKey("core_models.City", on_delete=models.DO_NOTHING, blank=True, null=True)

    zip = models.CharField(max_length=255, blank=True, null=True, db_column="ZIP")
    district = models.CharField(max_length=255, blank=True, null=True)

    province = models.ForeignKey("core_models.Province", on_delete=models.DO_NOTHING, blank=True, null=True)
    country = models.ForeignKey("core_models.Country", on_delete=models.DO_NOTHING, blank=True, null=True)

    continent = models.CharField(max_length=255, blank=True, null=True)

    customer = models.ForeignKey("core_models.Customer", on_delete=models.DO_NOTHING, blank=True, null=True)

    bu = models.ForeignKey("core_models.Bu", on_delete=models.DO_NOTHING, blank=True, null=True)
    company = models.ForeignKey("core_models.Company", on_delete=models.DO_NOTHING, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "address"
