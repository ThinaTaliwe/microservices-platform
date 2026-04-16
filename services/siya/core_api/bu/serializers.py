from rest_framework import serializers
from core_models.models.bu import Bu


class BuSerializer(serializers.ModelSerializer):
    class Meta:
        model = Bu
        fields = "__all__"
