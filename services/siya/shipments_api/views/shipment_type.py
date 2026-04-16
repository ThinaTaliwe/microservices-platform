from rest_framework import viewsets
from core_models.models import ShipmentType
from shipments_api.serializers.shipment_type import ShipmentTypeSerializer


class ShipmentTypeViewSet(viewsets.ReadOnlyModelViewSet):
    queryset = ShipmentType.objects.all().order_by("id")
    serializer_class = ShipmentTypeSerializer
