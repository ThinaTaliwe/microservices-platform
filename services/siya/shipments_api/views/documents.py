import os
from pathlib import Path
from django.conf import settings
from django.http import FileResponse, Http404
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from rest_framework.parsers import MultiPartParser, FormParser


ALLOWED_EXTS = {".jpg", ".jpeg", ".png", ".webp", ".pdf"}
MAX_BYTES = 25 * 1024 * 1024  # 25MB


def shipment_folder(shipment_id: int) -> Path:
    base = Path("/data/shipment-docs").resolve()
    folder = (base / str(shipment_id)).resolve()

    # Prevent path traversal
    if base not in folder.parents and folder != base:
        raise Http404()

    folder.mkdir(parents=True, exist_ok=True)
    return folder


def safe_filename(name: str) -> str:
    name = os.path.basename(name)
    name = name.replace("\x00", "")
    return name


def resolve_file_path(shipment_id: int, filename: str) -> Path:
    folder = shipment_folder(shipment_id)
    filename = safe_filename(filename)
    path = (folder / filename).resolve()

    if folder not in path.parents:
        raise Http404()

    return path


class ShipmentDocumentListCreate(APIView):
    parser_classes = [MultiPartParser, FormParser]

    def get(self, request, shipment_id: int):
        folder = shipment_folder(shipment_id)
        documents = []

        for p in sorted(folder.iterdir()):
            if not p.is_file():
                continue

            ext = p.suffix.lower()
            if ext not in ALLOWED_EXTS:
                continue

            documents.append({
                "name": p.name,
                "size_bytes": p.stat().st_size,
                "url": request.build_absolute_uri(
                    f"/siya/api/shipments/shipments/{shipment_id}/documents/{p.name}"
                )
            })

        return Response({
            "shipment_id": shipment_id,
            "documents": documents
        })

    def post(self, request, shipment_id: int):
        if "file" not in request.FILES:
            return Response({"detail": "Missing file"}, status=400)

        f = request.FILES["file"]

        if f.size > MAX_BYTES:
            return Response({"detail": "File too large"}, status=413)

        original = safe_filename(f.name)
        ext = Path(original).suffix.lower()

        if ext not in ALLOWED_EXTS:
            return Response({"detail": "File type not allowed"}, status=415)

        folder = shipment_folder(shipment_id)

        target = folder / original

        # Avoid collisions
        if target.exists():
            stem = Path(original).stem
            for i in range(1, 1000):
                candidate = folder / f"{stem}-{i}{ext}"
                if not candidate.exists():
                    target = candidate
                    break

        with target.open("wb") as out:
            for chunk in f.chunks():
                out.write(chunk)

        return Response({
            "name": target.name,
            "size_bytes": target.stat().st_size,
            "url": request.build_absolute_uri(
                f"/siya/api/shipments/shipments/{shipment_id}/documents/{target.name}"
            )
        }, status=201)


class ShipmentDocumentDetail(APIView):

    def get(self, request, shipment_id: int, filename: str):
        path = resolve_file_path(shipment_id, filename)

        if not path.exists() or not path.is_file():
            raise Http404()

        response = FileResponse(open(path, "rb"))
        response["Content-Disposition"] = f'inline; filename="{path.name}"'
        return response

    def delete(self, request, shipment_id: int, filename: str):
        path = resolve_file_path(shipment_id, filename)

        if not path.exists() or not path.is_file():
            raise Http404()

        path.unlink()
        return Response(status=204)
