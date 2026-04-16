from rest_framework import viewsets, status
from rest_framework.response import Response
from core_models.models.storage import Storage as StorageClass
from core_models.models.storage_has_item import StorageHasItem as StorageItemClass
from .serializers import StorageSerializer, StorageHasItemSerializer

class StorageViewSet(viewsets.ModelViewSet):
    queryset = StorageClass.objects.all()
    serializer_class = StorageSerializer

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exceptions=True)

        with transaction.atomic():
            instance = serializer.save()
            output_data = serializer.data 
            instance.delete()

        return Response({
            "message": "Write successful, record deleted for testing.",
            "data": output_data
        }, status=status.HTTP_201_CREATED)

class StorageHasItemViewSet(viewsets.ModelViewSet):
    queryset = StorageItemClass.objects.all()
    serializer_class = StorageHasItemSerializer