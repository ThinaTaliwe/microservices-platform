from rest_framework import viewsets
from core_models.models import ShipmentHasShipment, ShipmentHasPreviousShipments
from shipments_api.serializers.shipment_links import (
    ShipmentHasShipmentSerializer,
    ShipmentHasPreviousShipmentsSerializer,
)


class ShipmentHasShipmentViewSet(viewsets.ModelViewSet):
    queryset = ShipmentHasShipment.objects.all().order_by("-id")
    serializer_class = ShipmentHasShipmentSerializer


class ShipmentHasPreviousShipmentsViewSet(viewsets.ModelViewSet):
    queryset = ShipmentHasPreviousShipments.objects.all().order_by("-id")
    serializer_class = ShipmentHasPreviousShipmentsSerializer
