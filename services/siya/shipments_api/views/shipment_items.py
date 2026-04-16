from rest_framework import viewsets
from core_models.models import ShipmentItems
from shipments_api.serializers.shipment_items import ShipmentItemsSerializer


class ShipmentItemsViewSet(viewsets.ModelViewSet):
    queryset = ShipmentItems.objects.all().order_by("-id")
    serializer_class = ShipmentItemsSerializer
