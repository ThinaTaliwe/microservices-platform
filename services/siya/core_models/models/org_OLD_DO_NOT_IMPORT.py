from django.db import models


class Bu(models.Model):
    """
    Business Unit (BU).
    Minimal model so foreign keys work.
    We'll expand it later if needed.
    """
    id = models.BigAutoField(primary_key=True)

    # These are optional placeholders; safe even if DB has more columns.
    name = models.CharField(max_length=255, blank=True, null=True)
    code = models.CharField(max_length=50, blank=True, null=True)

    class Meta:
        managed = False
        db_table = "bu"
