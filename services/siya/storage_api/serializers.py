from rest_framework import serializers
from core_models.models.storage import Storage
from core_models.models.storage_has_item import StorageHasItem

class StorageHasItemSerializer(serializers.ModelSerializer):
    class Meta: 
        model = StorageHasItem
        fields = "__all__"

class StorageSerializer(serializers.ModelSerializer):
    storage_items = StorageHasItemSerializer(
        many=True, 
        read_only=True, 
        source="StorageHasItem_set" 
    )

    class Meta:
        model = Storage
        fields = ['id', 'storage_start_time', 'storage_end_time', 'bu', 'storage_items']

    def validate(self, data):
        start = data.get("storage_start_time", getattr(self.instance, 'storage_start_time', None))
        end = data.get("storage_end_time", getattr(self.instance, 'storage_end_time', None))
        
        if "bu" in data and not data.get("bu"):
            raise serializers.ValidationError({"bu": "Storage must belong to a Business Unit."})

        if start and end and start > end:
            raise serializers.ValidationError({
                "storage_end_time": "The end time cannot be earlier than the start time."
            })

        return data