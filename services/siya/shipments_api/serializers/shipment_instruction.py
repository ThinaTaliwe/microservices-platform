from rest_framework import serializers
from core_models import models as M

class ShipmentInstructionSerializer(serializers.ModelSerializer):
    class Meta:
        model = M.ShipmentInstruction
        fields = "__all__"


class ShipmentInstructionHasItemSerializer(serializers.ModelSerializer):
    class Meta:
        model = M.ShipmentInstructionHasItem
        fields = "__all__"


class ShipmentInstructionHasShipmentInstructionSerializer(serializers.ModelSerializer):
    class Meta:
        model = M.ShipmentInstructionHasShipmentInstruction
        fields = "__all__"
