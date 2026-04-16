from django.db import models


class Address(models.Model):
    """
    Placeholder for address table.
    We'll replace with real fields after we inspectdb 'address'.
    """
    id = models.BigAutoField(primary_key=True)

    class Meta:
        managed = False
        db_table = "address"


class Site(models.Model):
    """
    Placeholder for site table (used by Warehouse).
    We'll replace with real fields later if needed.
    """
    id = models.BigAutoField(primary_key=True)

    class Meta:
        managed = False
        db_table = "site"


class InstructionType(models.Model):
    """
    Placeholder for instruction_type table.
    We'll replace with real fields after we inspectdb 'instruction_type'.
    """
    id = models.BigAutoField(primary_key=True)

    class Meta:
        managed = False
        db_table = "instruction_type"


class Warehouse(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    site = models.ForeignKey(Site, models.DO_NOTHING, blank=True, null=True)
    short_code = models.CharField(max_length=5, blank=True, null=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    address = models.ForeignKey(Address, models.DO_NOTHING)
    intransit_warehouse = models.IntegerField(blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "warehouse"


class Location(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    warehouse = models.ForeignKey(Warehouse, models.DO_NOTHING)
    short_code = models.CharField(max_length=10, blank=True, null=True)
    name = models.CharField(max_length=45, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)
    deleted_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "location"

class Uom(models.Model):
    """
    Placeholder for unit-of-measure table.
    """
    id = models.BigAutoField(primary_key=True)

    class Meta:
        managed = False
        db_table = "uom"


class HarmonisationCode(models.Model):
    """
    Placeholder for harmonisation_code table.
    """
    id = models.BigAutoField(primary_key=True)

    class Meta:
        managed = False
        db_table = "harmonisation_code"
