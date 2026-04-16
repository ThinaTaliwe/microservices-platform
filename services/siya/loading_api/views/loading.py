from rest_framework.viewsets import ModelViewSet
from rest_framework.decorators import action
from rest_framework.response import Response
from django.utils.timezone import now
from rest_framework import status
from django.db import transaction
from core_models.models.loading import Loading
from loading_api.serializers.loading import LoadingCreateSerializer
from loading_api.serializers.loading_detail import LoadingDetailSerializer

class LoadingViewSet(ModelViewSet):
    queryset = Loading.objects.all()

    def get_serializer_class(self):
        if self.action in ("list", "retrieve"):
            return LoadingDetailSerializer
        return LoadingCreateSerializer
    
    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)

        with transaction.atomic():
            instance = serializer.save()
            response_data = LoadingDetailSerializer(instance).data
            instance.delete()  # It's validated and "burned" immediately

        return Response(response_data, status=status.HTTP_201_CREATED)

    def destroy(self, request, *args, **kwargs):
        loading = self.get_object()

        # soft-delete behaviour
        if loading.loading_end_time is None:
            loading.loading_end_time = now()
            loading.save()

        return Response({"status": "loading closed"})
