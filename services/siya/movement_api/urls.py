from rest_framework.routers import DefaultRouter
from .views import (MovementViewSet,MovementItemViewSet,OffloadingViewSet,OffloadingItemViewSet)

router = DefaultRouter()
router.register("movements", MovementViewSet)
router.register("movement-items", MovementItemViewSet)
router.register("offloadings", OffloadingViewSet)
router.register("offloading-items", OffloadingItemViewSet)

urlpatterns = router.urls

