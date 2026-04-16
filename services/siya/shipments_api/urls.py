from rest_framework.routers import DefaultRouter
from django.urls import path


from shipments_api.views.documents import (
    ShipmentDocumentListCreate,
    ShipmentDocumentDetail,
)

from shipments_api.views.shipment import ShipmentViewSet
from shipments_api.views.shipment_items import ShipmentItemsViewSet
from shipments_api.views.shipment_type import ShipmentTypeViewSet
from shipments_api.views.shipment_links import (
    ShipmentHasShipmentViewSet,
    ShipmentHasPreviousShipmentsViewSet,
)

from shipments_api.views.shipment_instruction import (
    ShipmentInstructionViewSet,
    ShipmentInstructionHasItemViewSet,
    ShipmentInstructionHasShipmentInstructionViewSet,
)


router = DefaultRouter()
router.register(r"shipments", ShipmentViewSet, basename="shipment")
router.register(r"shipment-items", ShipmentItemsViewSet, basename="shipment-items")
router.register(r"shipment-types", ShipmentTypeViewSet, basename="shipment-type")
router.register(r"shipment-has-shipment", ShipmentHasShipmentViewSet, basename="shipment-has-shipment")
router.register(r"shipment-has-previous-shipments", ShipmentHasPreviousShipmentsViewSet, basename="shipment-has-previous-shipments")

router.register(r"shipment-instructions", ShipmentInstructionViewSet, basename="shipment-instruction")
router.register(r"shipment-instruction-items", ShipmentInstructionHasItemViewSet, basename="shipment-instruction-item")
router.register(r"shipment-instruction-links", ShipmentInstructionHasShipmentInstructionViewSet, basename="shipment-instruction-link")


urlpatterns = router.urls + [
    path(
        "shipments/<int:shipment_id>/documents/",
        ShipmentDocumentListCreate.as_view(),
        name="shipment-documents"
    ),
    path(
        "shipments/<int:shipment_id>/documents/<path:filename>",
        ShipmentDocumentDetail.as_view(),
        name="shipment-document-detail"
    ),
]

