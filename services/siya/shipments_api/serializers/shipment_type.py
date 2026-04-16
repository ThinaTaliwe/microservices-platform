from rest_framework import serializers
from core_models.models import ShipmentType


class ShipmentTypeSerializer(serializers.ModelSerializer):
    class Meta:
        model = ShipmentType
        fields = "__all__"
