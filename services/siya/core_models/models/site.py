from django.db import models


class Site(models.Model):
    id = models.BigAutoField(primary_key=True)

    # Keep minimal for now. We can expand later if needed.
    name = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        managed = False
        db_table = "site"
