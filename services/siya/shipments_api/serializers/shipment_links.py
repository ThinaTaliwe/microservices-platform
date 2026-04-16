from rest_framework import serializers
from core_models.models import ShipmentHasShipment, ShipmentHasPreviousShipments


class ShipmentHasShipmentSerializer(serializers.ModelSerializer):
    class Meta:
        model = ShipmentHasShipment
        fields = "__all__"


class ShipmentHasPreviousShipmentsSerializer(serializers.ModelSerializer):
    class Meta:
        model = ShipmentHasPreviousShipments
        fields = "__all__"
