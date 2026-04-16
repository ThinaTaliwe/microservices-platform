from .shipsgo_service import shipsgo_track_container
from .searates_service import searates_track_container


def track_container(container):

    try:
        shipsgo_data = shipsgo_track_container(container)

        if shipsgo_data:
            return {
                "provider": "shipsgo",
                "data": shipsgo_data
            }

    except Exception:
        pass

    try:
        searates_data = searates_track_container(container)

        if searates_data:
            return {
                "provider": "searates",
                "data": searates_data
            }

    except Exception:
        pass

    return {"error": "All providers failed"}