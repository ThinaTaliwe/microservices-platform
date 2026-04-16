from django.urls import path, include

urlpatterns = [
    path("bu/", include("core_api.bu.urls")),
    path("addresses/", include("core_api.addresses.urls")),
]
