from rest_framework import viewsets
from core_models.models import Shipment
from shipments_api.serializers.shipment import ShipmentSerializer


class ShipmentViewSet(viewsets.ModelViewSet):
    queryset = Shipment.objects.all().order_by("-id")
    serializer_class = ShipmentSerializer
