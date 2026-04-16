from rest_framework.routers import DefaultRouter
from .views.loading import LoadingViewSet
from .views.loading_items import LoadingHasItemViewSet

router = DefaultRouter()
router.register(r"loadings", LoadingViewSet, basename="loading")
router.register(r"loading-items", LoadingHasItemViewSet, basename="loading-items")

urlpatterns = router.urls
