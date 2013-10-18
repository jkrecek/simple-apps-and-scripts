package com.frca.jacobianapp;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.graphics.Color;
import android.os.Bundle;
import android.os.Handler;
import android.support.v7.app.ActionBarActivity;
import android.util.Log;
import android.view.Gravity;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.LinearLayout;

import java.util.Random;

public class MainActivity extends ActionBarActivity {

    private int HEIGHT;
    private int WIDTH;
    private static final int RANGE = 255;

    private int[][] values;
    private View[][] views;

    private Random random = new Random(System.currentTimeMillis());

    final Handler mHandler = new Handler();

    private LinearLayout table;

    private AppTimer timer = new AppTimer();

    private int counter;

    private int DPPP;

    private boolean started = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        started = false;
        timer = new AppTimer();


        table = new LinearLayout(this);
        table.setOrientation(LinearLayout.VERTICAL);
        table.setGravity(Gravity.CENTER_VERTICAL);
        table.setLayoutParams(new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.MATCH_PARENT));

        setContentView(table);
    }

    @Override
    public void onWindowFocusChanged(boolean hasFocus) {
        super.onWindowFocusChanged(hasFocus);

        if (started)
            return;

        started = true;

        int tempHeight = getDip(table.getHeight());
        int tempWidth= getDip(table.getWidth());

        int higherValue = tempHeight > tempWidth ? tempHeight : tempWidth;
        double threshold = 400.d;
        DPPP = (int)Math.ceil(higherValue / threshold);
        Log.e("DPPP", String.valueOf(DPPP));

        HEIGHT = tempHeight / DPPP;
        WIDTH = tempWidth / DPPP;

        values = new int[HEIGHT][WIDTH];
        views = new View[HEIGHT][WIDTH];

        Log.e("HEIGHT", String.valueOf(HEIGHT));
        Log.e("WIDTH", String.valueOf(WIDTH));

        getSupportActionBar().setTitle(String.valueOf(WIDTH) + "x" + String.valueOf(HEIGHT) + ", DPPP: "+ String.valueOf(DPPP));

        timer.addEvent("Table created");

        final LinearLayout.LayoutParams rowParams = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, getPix(DPPP));
        final LinearLayout.LayoutParams cellParams = new LinearLayout.LayoutParams(getPix(DPPP), LinearLayout.LayoutParams.MATCH_PARENT);

        timer.addEvent("Layouts created");

        for (int line = 0; line < HEIGHT; ++line) {

            LinearLayout row = new LinearLayout(this);
            row.setOrientation(LinearLayout.HORIZONTAL);
            row.setGravity(Gravity.CENTER_HORIZONTAL);
            row.setLayoutParams(rowParams);

            if (line == 0)
                timer.addEvent("First row created");

            for (int col = 0; col < WIDTH; ++col) {
                View titleView = new View(this);
                titleView.setLayoutParams(cellParams);
                views[line][col] = titleView;
                row.addView(titleView);
            }

            if (line == 0)
                timer.addEvent("Text Views created");

            table.addView(row);

        }

        timer.addEvent("Whole view created");

        for (int line = 0; line < HEIGHT; ++line) {
            for (int col = 0; col < WIDTH; ++col) {
                setViewColor(line, col, getRandomColor());
            }
        }

        timer.addEvent("Numbers set");

        setContentView(table);

        timer.addEvent("View was set");

        timer.outputEvents();
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch(item.getOrder()) {
            case 101:
                counter = 0;
                timer = new AppTimer();
                scheduleRecalculate();
                return true;
            default:
                return super.onOptionsItemSelected(item);
        }
    }


    private void setViewColor(int lane, int col, int value)  {
        values[lane][col] = value;
        views[lane][col].setBackgroundColor(value);
    }

    private int getRandomColor() {
        int red = random.nextInt(RANGE+1);
        int green = random.nextInt(RANGE+1);
        int blue = random.nextInt(RANGE+1);

        int theColor = Color.rgb(red, green, blue);
        return theColor;
    }

    private int mixColors(int color1, int color2) {
        int red = Math.round((Color.red(color1) + Color.red(color2)) * 0.5f);
        int green = Math.round((Color.green(color1) + Color.green(color2)) * 0.5f);
        int blue = Math.round((Color.blue(color1) + Color.blue(color2)) * 0.5f);

        return Color.rgb(red, green, blue);

    }

    private void scheduleRecalculate() {
        if (counter < 10)
            mHandler.postDelayed(new Runnable() {
                @Override
                public void run() {
                    recalculate();
                }
            }, 100);
        else {
            timer.outputEvents();
            onResult();
        }
    }

    private void recalculate() {
        ++counter;

        int currentVal;

        for (int line = 0; line < HEIGHT; ++line) {
            for (int col = 0; col < WIDTH; ++col) {
                currentVal = values[line][col];

                if (line >= 1)
                    currentVal = mixColors(currentVal, values[line - 1][col]);

                if (line < HEIGHT - 1)
                    currentVal = mixColors(currentVal, values[line + 1][col]);

                if (col >= 1)
                    currentVal = mixColors(currentVal, values[line][col - 1]);

                if (col < WIDTH - 1)
                    currentVal = mixColors(currentVal, values[line][col + 1]);

                values[line][col] = currentVal;
            }
        }

        updateLayout();

        timer.addEvent("Finished step nr. " + String.valueOf(counter));
        getSupportActionBar().setTitle("Finished step nr. " + String.valueOf(counter));

        scheduleRecalculate();
    }

    public void updateLayout() {
        for (int line = 0; line < HEIGHT; ++line) {
            for (int col = 0; col < WIDTH; ++col) {
                View view = views[line][col];
                int color = values[line][col];

                view.setBackgroundColor(color);
            }
        }
    }

    public int getDip(int pixel) {
        float scale = getBaseContext().getResources().getDisplayMetrics().density;
        return (int) ((pixel - 0.5f)/scale);
    }

    public int getPix(int dip) {
        float scale = getBaseContext().getResources().getDisplayMetrics().density;
        return (int) (dip * scale + 0.5f);
    }

    private void onResult() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);

        builder.setTitle("Result")
            .setMessage(timer.getResult())
            .setPositiveButton(android.R.string.ok, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                    dialogInterface.dismiss();
                }
            });

        builder.create().show();
    }
}
