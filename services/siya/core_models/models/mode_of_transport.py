from django.db import models


class ModeOfTransport(models.Model):
    id = models.BigAutoField(primary_key=True)

    name = models.CharField(max_length=255, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)

    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "mode_of_transport"
