from rest_framework.viewsets import ModelViewSet
from core_models.models.bu import Bu
from .serializers import BuSerializer


class BuViewSet(ModelViewSet):
    serializer_class = BuSerializer
    queryset = Bu.objects.all().order_by("-id")
