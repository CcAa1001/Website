class ApiConfig {
  // Update these with your actual Laravel backend URL
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  // API Endpoints
  static const String products = '/products';
  static const String categories = '/categories';
  static const String tables = '/tables';
  static const String paymentMethods = '/payment-methods';
  static const String orders = '/orders';

  // Headers
  static Map<String, String> get headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };

  static Map<String, String> authHeaders(String token) => {
        ...headers,
        'Authorization': 'Bearer $token',
      };
}
