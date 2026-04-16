from rest_framework import serializers
from core_models.models.loading_has_item import LoadingHasItem

class LoadingHasItemSerializer(serializers.ModelSerializer):
    class Meta:
        model = LoadingHasItem
        fields = ("item", "quantity")

    def validate_quantity(self, value):
        if value is None or value <= 0:
            raise serializers.ValidationError("Quantity must be greater than zero.")
        return value
