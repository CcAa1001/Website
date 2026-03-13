import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/pos_provider.dart';
import '../widgets/pos_header.dart';
import '../widgets/products_section.dart';
import '../widgets/cart_section.dart';
import '../widgets/modifier_modal.dart';
import '../widgets/table_selector_modal.dart';
import '../widgets/payment_modal.dart';
import '../widgets/parked_orders_modal.dart';

class POSScreen extends ConsumerStatefulWidget {
  const POSScreen({super.key});

  @override
  ConsumerState<POSScreen> createState() => _POSScreenState();
}

class _POSScreenState extends ConsumerState<POSScreen> {
  bool _showModifierModal = false;
  bool _showTableSelector = false;
  bool _showPaymentModal = false;
  bool _showParkedOrders = false;
  String? _currentProductId;

  void _openModifierModal(String productId) {
    setState(() {
      _currentProductId = productId;
      _showModifierModal = true;
    });
  }

  void _closeModifierModal() {
    setState(() {
      _showModifierModal = false;
      _currentProductId = null;
    });
  }

  void _openTableSelector() {
    setState(() {
      _showTableSelector = true;
    });
  }

  void _closeTableSelector() {
    setState(() {
      _showTableSelector = false;
    });
  }

  void _openPaymentModal() {
    setState(() {
      _showPaymentModal = true;
    });
  }

  void _closePaymentModal() {
    setState(() {
      _showPaymentModal = false;
    });
  }

  void _toggleParkedOrders() {
    setState(() {
      _showParkedOrders = !_showParkedOrders;
    });
  }

  @override
  Widget build(BuildContext context) {
    final posState = ref.watch(posProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      body: Stack(
        children: [
          Column(
            children: [
              // Header
              POSHeader(
                parkedOrdersCount: posState.parkedOrders.length,
                onParkedOrdersTap: _toggleParkedOrders,
              ),
              
              // Main Content
              Expanded(
                child: Row(
                  children: [
                    // Left: Products Section
                    Expanded(
                      flex: 7,
                      child: ProductsSection(
                        onProductTap: _openModifierModal,
                      ),
                    ),
                    
                    // Right: Cart Section
                    SizedBox(
                      width: 450,
                      child: CartSection(
                        onTableSelectorTap: _openTableSelector,
                        onPaymentTap: _openPaymentModal,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          
          // Modals
          if (_showModifierModal && _currentProductId != null)
            ModifierModal(
              productId: _currentProductId!,
              onClose: _closeModifierModal,
            ),
          
          if (_showTableSelector)
            TableSelectorModal(
              onClose: _closeTableSelector,
            ),
          
          if (_showPaymentModal)
            PaymentModal(
              onClose: _closePaymentModal,
            ),
          
          if (_showParkedOrders)
            ParkedOrdersModal(
              onClose: _toggleParkedOrders,
            ),
        ],
      ),
    );
  }
}
