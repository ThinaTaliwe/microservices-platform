from rest_framework import viewsets
from core_models import models as M
from shipments_api.serializers.shipment_instruction import (
    ShipmentInstructionSerializer,
    ShipmentInstructionHasItemSerializer,
    ShipmentInstructionHasShipmentInstructionSerializer,
)

class ShipmentInstructionViewSet(viewsets.ModelViewSet):
    queryset = M.ShipmentInstruction.objects.all()
    serializer_class = ShipmentInstructionSerializer


class ShipmentInstructionHasItemViewSet(viewsets.ModelViewSet):
    queryset = M.ShipmentInstructionHasItem.objects.all()
    serializer_class = ShipmentInstructionHasItemSerializer


class ShipmentInstructionHasShipmentInstructionViewSet(viewsets.ModelViewSet):
    queryset = M.ShipmentInstructionHasShipmentInstruction.objects.all()
    serializer_class = ShipmentInstructionHasShipmentInstructionSerializer
