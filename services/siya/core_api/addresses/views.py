from rest_framework.viewsets import ModelViewSet
from core_models.models.address import Address
from .serializers import AddressSerializer


class AddressViewSet(ModelViewSet):
    serializer_class = AddressSerializer
    queryset = Address.objects.all().order_by("-id")
