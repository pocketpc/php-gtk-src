/*
 * PHP-GTK - The PHP language bindings for GTK+
 *
 * Copyright (C) 2001 Andrei Zmievski <andrei@ispi.net>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
 
/* $Id$: */

#ifndef _PHP_GTK_H
#define _PHP_GTK_H

#include "php_gtk_module.h"
#include "gtk/gtkinvisible.h"

#if HAVE_PHP_GTK

#include <gtk/gtk.h>

extern int le_gtk;
extern int le_gdk_event;
extern int le_gdk_window;
extern int le_gdk_color;
extern int le_gdk_colormap;

extern zend_class_entry *gdk_event_ce;
extern zend_class_entry *gdk_window_ce;
extern zend_class_entry *gdk_color_ce;
extern zend_class_entry *gdk_colormap_ce;

/* Useful macros. */
#define PHP_GTK_GET(w) 			((GtkObject *)php_gtk_get_object(w, le_gtk))
#define PHP_GDK_EVENT_GET(w)	((GdkEvent *)php_gtk_get_object(w, le_gdk_event))
#define PHP_GDK_WINDOW_GET(w)	((GdkWindow *)php_gtk_get_object(w, le_gdk_window))
#define PHP_GDK_COLOR_GET(w)	((GdkColor *)php_gtk_get_object(w, le_gdk_color))
#define PHP_GDK_COLORMAP_GET(w)	((GdkColormap *)php_gtk_get_object(w, le_gdk_colormap))

/* True globals. */
extern GHashTable *php_gtk_class_hash;

/* Function declarations. */

void php_gtk_register_classes(void);
void php_gtk_register_types(int module_number);
void php_gtk_register_constants(int module_number ELS_DC);
void php_gtk_set_object(zval *wrapper, void *obj, int rsrc_type);
void *php_gtk_get_object(zval *wrapper, int rsrc_type);
int php_gtk_get_enum_value(GtkType enum_type, zval *enum_val, int *result);
void php_gtk_destroy_notify(gpointer user_data);
void php_gtk_callback_marshal(GtkObject *o, gpointer data, guint nargs, GtkArg *args);
void php_gtk_ret_from_value(GtkArg *ret, zval *value);
zval *php_gtk_args_as_hash(int nargs, GtkArg *args);
zval *php_gtk_arg_as_value(GtkArg *arg);
int php_gtk_get_flag_value(GtkType flag_type, zval *flag_val, int *result);

/* Constructors and initializers. */
void php_gtk_object_init(GtkObject *obj, zval *wrapper);
zval *php_gtk_new(GtkObject *obj);
zval *php_gdk_event_new(GdkEvent *obj);
zval *php_gdk_window_new(GdkWindow *obj);
zval *php_gdk_color_new(GdkColor *obj);
zval *php_gdk_colormap_new(GdkColormap *obj);

/* Utility functions. */
int php_gtk_parse_args(int argc, char *format, ...);
int php_gtk_parse_args_quiet(int argc, char *format, ...);
int php_gtk_check_class(zval *wrapper, zend_class_entry *expected_ce);
void php_gtk_invalidate(zval *wrapper);
zend_bool php_gtk_check_callable(zval *function);
zval *php_gtk_array_as_hash(zval ***values, int num_values, int start, int length);
zval ***php_gtk_hash_as_array(zval *hash);
zval ***php_gtk_func_args(int argc);
zval *php_gtk_func_args_as_hash(int argc, int start, int length);
zval *php_gtk_build_value(char *format, ...);

#define NOT_STATIC_METHOD() \
	if (!this_ptr) { \
		php_error(E_WARNING, "%s() is not a static method", get_active_function_name()); \
		return; \
	}

#endif /* HAVE_PHP_GTK */

#endif	/* _PHP_GTK_H */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
