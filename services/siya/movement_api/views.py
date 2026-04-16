from rest_framework import viewsets
from rest_framework.decorators import action
from rest_framework.response import Response
from django.utils.timezone import now

from core_models.models import (Movement,MovementHasItem,Offloading,OffloadingHasItem)

from .serializers import (MovementSerializer,MovementHasItemSerializer,OffloadingSerializer,OffloadingHasItemSerializer)

class MovementViewSet(viewsets.ModelViewSet):
    queryset = Movement.objects.all()
    serializer_class = MovementSerializer

    ### Start movement
    @action(detail=True, methods=['post'])
    def start(self, request, pk=None):
        movement = self.get_object()
        movement.movement_start_time = now()
        movement.save()
        return Response({"message": "Movement started"})

    ### Complete movement
    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        movement = self.get_object()
        movement.movement_end_time = now()
        movement.save()
        return Response({"message": "Movement completed"})


class MovementItemViewSet(viewsets.ModelViewSet):
    queryset = MovementHasItem.objects.all()
    serializer_class = MovementHasItemSerializer


class OffloadingViewSet(viewsets.ModelViewSet):
    queryset = Offloading.objects.all()
    serializer_class = OffloadingSerializer

    # Start offloading
    @action(detail=True, methods=['post'])
    def start(self, request, pk=None):
        offloading = self.get_object()
        offloading.offloading_start_time = now()
        offloading.save()
        return Response({"message": "Offloading started"})

    # Complete offloading
    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        offloading = self.get_object()
        offloading.offloading_end_time = now()
        offloading.save()
        return Response({"message": "Offloading completed"})


class OffloadingItemViewSet(viewsets.ModelViewSet):
    queryset = OffloadingHasItem.objects.all()
    serializer_class = OffloadingHasItemSerializer


 
