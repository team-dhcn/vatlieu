# Sử dụng image PHP kèm Apache tiêu chuẩn để chạy giao diện web
FROM php:8.2-apache

# Cài đặt extension bắt buộc để kết nối MySQL từ PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Sao chép toàn bộ mã nguồn dự án hiện tại vào container
COPY . /var/www/html/

# Phân quyền cho Apache đọc ghi mã nguồn mượt mà
RUN chown -R www-data:www-data /var/www/html

# Mở cổng web nội bộ 80 của container
EXPOSE 80
