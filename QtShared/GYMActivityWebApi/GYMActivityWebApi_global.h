#ifndef GYMACTIVITYWEBAPI_GLOBAL_H
#define GYMACTIVITYWEBAPI_GLOBAL_H

#include <QtCore/qglobal.h>

#if defined(GYMACTIVITYWEBAPI_LIBRARY)
#  define GYMACTIVITYWEBAPISHARED_EXPORT Q_DECL_EXPORT
#else
#  define GYMACTIVITYWEBAPISHARED_EXPORT Q_DECL_IMPORT
#endif

#endif // GYMACTIVITYWEBAPI_GLOBAL_H
