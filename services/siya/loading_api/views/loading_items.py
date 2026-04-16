from rest_framework import viewsets
from core_models.models import LoadingHasItem
from loading_api.serializers.loading_item import LoadingHasItemSerializer


class LoadingHasItemViewSet(viewsets.ModelViewSet):
    queryset = LoadingHasItem.objects.all().order_by("-id")
    serializer_class = LoadingHasItemSerializer
