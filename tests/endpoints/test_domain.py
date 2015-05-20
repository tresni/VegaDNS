import json

from mock import MagicMock

from tests.endpoints import AbstractEndpointTest
import vegadns.api.endpoints.domain
from vegadns.api import app


class TestDomain(AbstractEndpointTest):
    def setUp(self):
        # Use Flask's test client
        self.test_app = app.test_client()

    def test_get_success(self):
        # mock get_domain and to_dict
        mock_value = {
            'owner': 0,
            'status': 'active',
            'group_owner': 0,
            'domain': 'vegadns.org',
            'domain_id': 1
        }
        mock_model = MagicMock()
        mock_model.to_dict = MagicMock(return_value=mock_value)
        vegadns.api.endpoints.domain.Domain.get_domain = MagicMock(
            return_value=mock_model
        )

        self.mock_auth('test@test.com', 'test')

        response = self.open_with_basic_auth(
            '/domains/1',
            'GET',
            'test@test.com',
            'test'
        )
        self.assertEqual(response.status, "200 OK")
        decoded = json.loads(response.data)
        self.assertEqual(decoded['status'], "ok")
        self.assertEqual(decoded['domain']['domain_id'], 1)