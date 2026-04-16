from rest_framework import serializers
from core_models.models import (Movement,MovementHasItem,Offloading,OffloadingHasItem)

class MovementSerializer(serializers.ModelSerializer):

    class Meta:
        model = Movement
        fields = "__all__"

    def validate(self, data):
        start = data.get("movement_start_time")
        end = data.get("movement_end_time")

        if start and end and end < start:
            raise serializers.ValidationError(
                "Movement end time cannot be before start time."
            )
        return data


class MovementHasItemSerializer(serializers.ModelSerializer):

    class Meta:
        model = MovementHasItem
        fields = "__all__"

    def validate(self, data):
        qty = data.get("quantity")

        if qty is not None and qty <= 0:
            raise serializers.ValidationError(
                "Quantity must be greater than zero."
            )
        return data


class OffloadingSerializer(serializers.ModelSerializer):

    class Meta:
        model = Offloading
        fields = "__all__"

    def validate(self, data):
        start = data.get("offloading_start_time")
        end = data.get("offloading_end_time")

        if start and end and end < start:
            raise serializers.ValidationError(
                "Offloading end time cannot be before start time."
            )
        return data


class OffloadingHasItemSerializer(serializers.ModelSerializer):

    class Meta:
        model = OffloadingHasItem
        fields = "__all__"

    def validate(self, data):
        qty = data.get("quantity")

        if qty is not None and qty <= 0:
            raise serializers.ValidationError(
                "Quantity must be greater than zero."
            )
        return data



