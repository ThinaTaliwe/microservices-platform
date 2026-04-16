from rest_framework.routers import DefaultRouter
from .views import BuViewSet

router = DefaultRouter()
router.register(r"", BuViewSet, basename="bu")

urlpatterns = router.urls
