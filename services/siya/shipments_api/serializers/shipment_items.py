from rest_framework import serializers
from core_models.models import ShipmentItems

class ShipmentItemsSerializer(serializers.ModelSerializer):
    class Meta:
        model = ShipmentItems
        fields = "__all__"
