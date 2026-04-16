from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import StorageViewSet, StorageHasItemViewSet

router = DefaultRouter()
router.register(r'storage', StorageViewSet, basename='storages')
router.register(r'storage-items', StorageHasItemViewSet, basename='storage-items')

urlpatterns = [
    path('',include(router.urls)),
]