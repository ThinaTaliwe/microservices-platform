from django.db import models


class Movement(models.Model):
    bu = models.ForeignKey("core_models.Bu", models.DO_NOTHING)
    parent_movement = models.ForeignKey("self", models.DO_NOTHING, blank=True, null=True)
    movement_start_time = models.DateTimeField(blank=True, null=True)
    movement_end_time = models.DateTimeField(blank=True, null=True)
    movement_reference = models.CharField(max_length=255, blank=True, null=True)
    created_at = models.DateTimeField(blank=True, null=True)
    updated_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = "movement"
