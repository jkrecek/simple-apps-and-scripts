#ifndef EXTRACTOR_H
#define EXTRACTOR_H

#include <QObject>
#include "UrlDownloader.h"
#include "Outputer.h"

class Bot : public QObject
{
    Q_OBJECT
    public:
        Bot(QObject *parent = 0);
        ~Bot() {}

    private:
        UrlDownloader* u;
};

#endif // EXTRACTOR_H
