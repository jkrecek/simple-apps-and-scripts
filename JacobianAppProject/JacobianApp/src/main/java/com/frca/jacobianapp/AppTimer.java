package com.frca.jacobianapp;

import android.util.Log;

import java.util.ArrayList;
import java.util.List;

public class AppTimer {

    private class Event {

        final public long timestamp;
        final public String info;

        public Event(String info) {
            timestamp = System.currentTimeMillis();
            this.info = info;
        }

        public Event getPrevious() {
            int idx = getOrder();
            if (idx == 0)
                return null;

            return events.get(idx - 1);
        }

        public long getDuration() {
            Event previous = getPrevious();
            if (previous == null)
                return timestamp - start_timestamp;

            return timestamp - previous.timestamp;
        }

        public int getOrder() {
            return events.indexOf(this);
        }

        private String getDurationString() {
            long duration = getDuration();
            if (duration < 1000L)
                return String.valueOf(duration) + " ms";
            else {
                return String.valueOf(duration/1000L) + " s";
            }
        }
    }

    private final long start_timestamp;

    private List<Event> events = new ArrayList<Event>();

    public AppTimer() {
        start_timestamp = System.currentTimeMillis();
    }

    public void addEvent(String info) {
        events.add(new Event(info));
    }

    public void outputEvents() {
        StringBuilder builder = new StringBuilder();

        for (Event event : events) {
            String first = event.getOrder() + ". " + event.info;
            String second = String.valueOf(event.getDuration()) + "ms";
            String third = String.valueOf(event.timestamp - start_timestamp) + "ms";

            builder.append('\n');

            builder.append(first);
            appendSpaces(builder, first.length() - 70);
            builder.append("| ");

            builder.append(second);
            appendSpaces(builder, second.length() - 10);
            builder.append("| ");

            builder.append(third);
        }

        Log.d("AppTimer", builder.toString());
    }

    private void appendSpaces(StringBuilder sb, int count) {
        if (count < 0)
            return;

        char[] spaces = new char[count];
        for (int i = 0; i < count; ++i)
            spaces[i] = ' ';
        sb.append(spaces);
    }

    public Event getMax() {
        Event max = null;
        for (Event event : events) {
            if (max == null || max.getDuration() < event.getDuration())
                max = event;
        }
        return max;
    }

    public Event getMin() {
        Event min = null;
        for (Event event : events) {
            if (min == null || min.getDuration() > event.getDuration())
                min = event;
        }
        return min;
    }

    public long getAverage() {
        int count = events.size();
        if (count > 0)
            return getTotal() / events.size();

        return 0;
    }

    public long getTotal() {
        Event lastEvent = events.get(events.size() - 1);
        if (lastEvent != null)
            return lastEvent.timestamp - start_timestamp;

        return 0;
    }

    public String getResult() {
        StringBuilder builder = new StringBuilder();

        builder.append("Your results are:\n")
            .append("Total time of test: " + String.valueOf(getTotal()) + "\n")
            .append("Total iteration: " + String.valueOf(events.size()) + "\n")
            .append("Fastest iteration: " + String.valueOf(getMin().getOrder()) + ". " + getMin().getDurationString() + "\n")
            .append("Slowest iteration: " + String.valueOf(getMax().getOrder()) + ". " + getMax().getDurationString() + "\n")
            .append("Average time per iteration: " + String.valueOf(getAverage()) + "\n");

        return builder.toString();
    }
}
