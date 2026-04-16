from django.db import models


class HarmonisationCode(models.Model):
    id = models.BigAutoField(primary_key=True)

    code = models.CharField(max_length=255, blank=True, null=True)
    name = models.CharField(max_length=255, blank=True, null=True)
    description = models.TextField(blank=True, null=True)

    chapter = models.CharField(max_length=255, blank=True, null=True)
    version = models.CharField(max_length=255, blank=True, null=True)

    country = models.ForeignKey("core_models.Country", on_delete=models.DO_NOTHING, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "harmonisation_code"
