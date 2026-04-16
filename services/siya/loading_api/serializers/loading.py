from rest_framework import serializers
from django.db import transaction
from core_models.models.loading_has_item import LoadingHasItem
from .loading_item import LoadingHasItemSerializer
from core_models.models.loading import Loading

class LoadingCreateSerializer(serializers.ModelSerializer):
    items = LoadingHasItemSerializer(many=True, write_only=True)

    class Meta:
        model = Loading
        fields = (
            "id",
            "bu",
            "loading_type",
            "parent_loading",
            "loading_reference",
            "loading_start_time",
            "items",
        )

    def validate_items(self, value):
        if not value or len(value) == 0:
            raise serializers.ValidationError("Loading must have at least one item.")
        return value

    @transaction.atomic
    def create(self, validated_data):
        items_data = validated_data.pop("items")

        loading = Loading.objects.create(**validated_data)

        for item in items_data:
            LoadingHasItem.objects.create(
                loading=loading,
                item=item["item"],
                quantity=item["quantity"],
            )

        return loading
