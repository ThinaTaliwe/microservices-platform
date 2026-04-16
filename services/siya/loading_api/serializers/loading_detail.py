from rest_framework import serializers
from core_models.models.loading import Loading
from core_models.models.loading_has_item import LoadingHasItem
from .loading_item import LoadingHasItemSerializer

class LoadingDetailSerializer(serializers.ModelSerializer):
    items = LoadingHasItemSerializer(
        source="loadinghasitem_set",
        many=True,
        read_only=True
    )

    class Meta:
        model = Loading
        fields = "__all__"
