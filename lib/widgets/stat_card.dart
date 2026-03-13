import 'package:flutter/material.dart';
import '../core/theme/app_theme.dart';

class StatCardData {
  final String label;
  final String value;
  final String? trend;
  final bool trendPositive;
  final String? subtitle;
  final IconData icon;
  final List<Color> gradientColors;

  const StatCardData({
    required this.label,
    required this.value,
    required this.icon,
    required this.gradientColors,
    this.trend,
    this.trendPositive = true,
    this.subtitle,
  });
}

class StatCard extends StatelessWidget {
  final StatCardData data;
  const StatCard({super.key, required this.data});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          // Gradient icon box
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: data.gradientColors,
              ),
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: data.gradientColors.first.withOpacity(0.3),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Icon(data.icon, color: Colors.white, size: 24),
          ),
          const SizedBox(width: 14),

          // Content
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  data.label,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: Colors.grey.shade600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  data.value,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.dark,
                    height: 1.1,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),

                // Trend or subtitle
                if (data.trend != null)
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: data.trendPositive
                          ? AppTheme.success.withOpacity(0.1)
                          : AppTheme.danger.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          data.trendPositive
                              ? Icons.trending_up
                              : Icons.trending_down,
                          size: 13,
                          color: data.trendPositive
                              ? AppTheme.success
                              : AppTheme.danger,
                        ),
                        const SizedBox(width: 3),
                        Flexible(
                          child: Text(
                            data.trend!,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: data.trendPositive
                                  ? AppTheme.success
                                  : AppTheme.danger,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  )
                else if (data.subtitle != null)
                  Text(
                    data.subtitle!,
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade500,
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
