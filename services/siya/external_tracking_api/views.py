from rest_framework.decorators import api_view
from rest_framework.response import Response
from .services.tracking_gateway import track_container


@api_view(["GET"])
def container_tracking_api(request):

    container = request.GET.get("number")

    if not container:
        return Response({"error": "container number required"}, status=400)

    data = track_container(container)

    return Response(data)